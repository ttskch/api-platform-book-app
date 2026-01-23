"use client";

import { Badge, Button, Group, Table } from "@mantine/core";
import createClient from "openapi-fetch";
import { useState } from "react";
import { components, paths } from "../lib/api/schema";

// ブラウザ用のAPIクライアント
const client = createClient<paths, "application/ld+json">({
  baseUrl: "http://localhost:8000",
  headers: {
    "Content-Type": "application/ld+json",
  },
});

type Article = components["schemas"]["Article.jsonld-article.read.list"];

type Props = {
  initialArticles: Article[];
};

export default function ArticleTable({ initialArticles }: Props) {
  const [articles, setArticles] = useState<Article[]>(initialArticles);

  const handleReload = async () => {
    const { data } = await client.GET("/api/articles");
    setArticles(data?.member ?? []);
  };

  return (
    <>
      <Group justify="flex-end" mb="lg">
        <Button onClick={handleReload}>リロード</Button>
      </Group>
      <Table mb="lg">
        <Table.Thead>
          <Table.Tr>
            <Table.Th>ID</Table.Th>
            <Table.Th>タイトル</Table.Th>
            <Table.Th>投稿日</Table.Th>
            <Table.Th>公開済み</Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody>
          {articles.map((article) => (
            <Table.Tr key={article.id}>
              <Table.Td>{article.id}</Table.Td>
              <Table.Td>{article.title}</Table.Td>
              <Table.Td>{article.date}</Table.Td>
              <Table.Td>
                {article.published ? (
                  <Badge color="blue">Yes</Badge>
                ) : (
                  <Badge color="gray">No</Badge>
                )}
              </Table.Td>
            </Table.Tr>
          ))}
        </Table.Tbody>
      </Table>
    </>
  );
}
